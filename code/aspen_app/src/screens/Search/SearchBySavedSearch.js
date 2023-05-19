import { CommonActions } from '@react-navigation/native';
import { Center, Button, Box, Badge, Text, FlatList, Heading, Stack, HStack, VStack, Pressable, Image, Container } from 'native-base';
import React, { Component } from 'react';
import CachedImage from 'expo-cached-image';

// custom components and helper files
import { loadError } from '../../components/loadError';
import { loadingSpinner } from '../../components/loadingSpinner';
import { userContext } from '../../context/user';
import { getFormats, savedSearchResults } from '../../util/search';
import { AddToList } from './AddToList';
import { getLists } from '../../util/api/list';
import { LibrarySystemContext, UserContext } from '../../context/initialContext';
import { navigateStack } from '../../helpers/RootNavigator';
import { getCleanTitle } from '../../helpers/item';
import { formatDiscoveryVersion } from '../../util/loadLibrary';
import { getTermFromDictionary } from '../../translations/TranslationService';

export default class SearchBySavedSearch extends Component {
     constructor() {
          super();
          this.state = {
               isLoading: true,
               isLoadingMore: false,
               data: [],
               searchMessage: null,
               page: 1,
               hasError: false,
               error: null,
               refreshing: false,
               filtering: false,
               endOfResults: false,
               dataMessage: null,
               lastListUsed: 0,
               language: 'en',
          };
          this.lastListUsed = 0;
          this.updateLastListUsed = this.updateLastListUsed.bind(this);
     }

     componentDidMount = async () => {
          //const level      = this.props.navigation.state.params.level;
          //const format     = this.props.navigation.state.params.format;
          //const searchType = this.props.navigation.state.params.searchType;
          const { navigation, route } = this.props;
          const libraryUrl = this.props.route.params.url;

          const params = this.props.route.params.url;
          const language = route.params?.language ?? 'en';
          this.setState({
               language: language,
          });

          await getLists(libraryUrl);
          await this._fetchResults();
          this._getLastListUsed();
     };

     _getLastListUsed = () => {
          if (this.context.user) {
               const user = this.context.user;
               this.lastListUsed = user.lastListUsed;
          }
     };

     updateLastListUsed = (id) => {
          this.setState({
               isLoading: true,
          });

          this.lastListUsed = id;

          this.setState({
               isLoading: false,
          });
     };

     _fetchResults = async () => {
          const { page } = this.state;
          const { navigation, route } = this.props;
          const category = route.params?.id ?? '';
          const libraryUrl = route.params?.url;
          const language = route.params?.language ?? 'en';

          await savedSearchResults(category, 25, page, libraryUrl, language).then((response) => {
               if (response.ok) {
                    const records = Object.values(response.data.result.items);

                    //console.log(records.length);
                    if (records.length > 0) {
                         this.setState((prevState, nextProps) => ({
                              data: page === 1 ? Array.from(response.data.result.items) : [...this.state.data, ...response.data.result.items],
                              isLoading: false,
                              isLoadingMore: false,
                              refreshing: false,
                         }));
                    } else {
                         if (page === 1 && records.length === 0) {
                              /* No search results were found */
                              this.setState({
                                   hasError: true,
                                   error: response.data.result.message,
                                   isLoading: false,
                                   isLoadingMore: false,
                                   refreshing: false,
                                   dataMessage: response.data.result.message,
                              });
                         } else {
                              /* Tried to fetch next page, but end of results */
                              this.setState({
                                   isLoading: false,
                                   isLoadingMore: false,
                                   refreshing: false,
                                   dataMessage: response.data.result.message,
                                   endOfResults: true,
                              });
                         }
                    }
               }
          });
     };

     _handleLoadMore = () => {
          this.setState(
               (prevState, nextProps) => ({
                    page: prevState.page + 1,
                    isLoadingMore: true,
               }),
               () => {
                    this._fetchResults();
               }
          );
     };

     renderItem = (item, library, lastListUsed) => {
          //console.log(item);
          const imageUrl = this.props.route.params.url + item.image;
          let formats = [];
          if (item.format) {
               formats = getFormats(item.format);
          }

          let isNew = false;
          if (typeof item.isNew !== 'undefined') {
               isNew = item.isNew;
          }

          return (
               <Pressable borderBottomWidth="1" _dark={{ borderColor: 'gray.600' }} borderColor="coolGray.200" pl="4" pr="5" py="2" onPress={() => this.onPressItem(item.id, library, item.title)}>
                    <HStack space={3}>
                         <VStack maxW="30%">
                              {isNew ? (
                                   <Container zIndex={1}>
                                        <Badge colorScheme="warning" shadow={1} mb={-3} ml={-1} _text={{ fontSize: 9 }}>
                                             {getTermFromDictionary(this.state.language, 'flag_new')}
                                        </Badge>
                                   </Container>
                              ) : null}
                              <CachedImage
                                   cacheKey={item.id}
                                   alt={item.title}
                                   source={{
                                        uri: `${imageUrl}`,
                                        expiresIn: 86400,
                                   }}
                                   style={{
                                        width: 100,
                                        height: 150,
                                        borderRadius: 4,
                                   }}
                                   resizeMode="cover"
                                   placeholderContent={
                                        <Box
                                             bg="warmGray.50"
                                             _dark={{
                                                  bgColor: 'coolGray.800',
                                             }}
                                             width={{
                                                  base: 100,
                                                  lg: 200,
                                             }}
                                             height={{
                                                  base: 150,
                                                  lg: 250,
                                             }}
                                        />
                                   }
                              />
                              <Badge
                                   mt={1}
                                   _text={{
                                        fontSize: 10,
                                        color: 'coolGray.600',
                                   }}
                                   bgColor="warmGray.200"
                                   _dark={{
                                        bgColor: 'coolGray.900',
                                        _text: { color: 'warmGray.400' },
                                   }}>
                                   {item.language}
                              </Badge>
                              <AddToList itemId={item.id} btnStyle="sm" />
                         </VStack>
                         <VStack w="65%">
                              <Text
                                   _dark={{ color: 'warmGray.50' }}
                                   color="coolGray.800"
                                   bold
                                   fontSize={{
                                        base: 'md',
                                        lg: 'lg',
                                   }}>
                                   {item.title}
                              </Text>
                              {item.author ? (
                                   <Text _dark={{ color: 'warmGray.50' }} color="coolGray.800">
                                        {getTermFromDictionary(this.state.language, 'by')} {item.author}
                                   </Text>
                              ) : null}
                              {item.format ? (
                                   <Stack mt={1.5} direction="row" space={1} flexWrap="wrap">
                                        {formats.map((format, i) => {
                                             return (
                                                  <Badge colorScheme="secondary" mt={1} variant="outline" rounded="4px" _text={{ fontSize: 12 }}>
                                                       {format}
                                                  </Badge>
                                             );
                                        })}
                                   </Stack>
                              ) : null}
                         </VStack>
                    </HStack>
               </Pressable>
          );
     };

     /*	getFormats = (data) => {
  let formats = [];

  data.map((item) => {
  let thisFormat = item.split("#");
  thisFormat = thisFormat[thisFormat.length - 1];
  formats.push(thisFormat);
  });

  formats = _.uniq(formats);
  return formats;
  }*/

     // handles the on press action
     onPressItem = (item, library, title) => {
          const { route } = this.props;
          const libraryContext = route.params?.libraryContext ?? [];
          const version = formatDiscoveryVersion(libraryContext.discoveryVersion);
          if (version >= '23.01.00') {
               navigateStack('SearchTab', 'SavedSearchResultItem', {
                    id: item,
                    title: getCleanTitle(title),
                    url: library.baseUrl,
               });
          } else {
               navigateStack('SearchTab', 'SavedSearchResultItem221200', {
                    id: item,
                    title: getCleanTitle(title),
                    url: library.baseUrl,
                    libraryContext: libraryContext,
               });
          }
     };

     // this one shouldn't probably ever load with the catches in the render, but just in case
     _listEmptyComponent = () => {
          const { navigation, route } = this.props;
          return (
               <Center flex={1}>
                    <Heading pt={5}>{getTermFromDictionary(this.state.language, 'no_results')}</Heading>
                    <Text bold w="75%" textAlign="center">
                         {route.params?.title}
                    </Text>
                    <Button mt={3} onPress={() => navigation.dispatch(CommonActions.goBack())}>
                         {getTermFromDictionary(this.state.language, 'new_search_button')}
                    </Button>
               </Center>
          );
     };

     _renderFooter = () => {
          if (!this.state.isLoadingMore) {
               return null;
          }
          return loadingSpinner();
     };

     static contextType = userContext;

     render() {
          const { navigation, route } = this.props;
          const library = this.context.library;

          //console.log(this.state.data);

          if (this.state.isLoading) {
               return loadingSpinner();
          }

          if (this.state.hasError && !this.state.dataMessage) {
               return loadError(this.state.error, this._fetchResults);
          }

          if (this.state.hasError && this.state.dataMessage) {
               return (
                    <Center flex={1}>
                         <Heading pt={5}>{getTermFromDictionary(this.state.language, 'no_results')}</Heading>
                         <Text bold w="75%" textAlign="center">
                              {route.params?.title}
                         </Text>
                         <Button mt={3} onPress={() => navigation.dispatch(CommonActions.goBack())}>
                              {getTermFromDictionary(this.state.language, 'new_search_button')}
                         </Button>
                    </Center>
               );
          }

          return (
               <Box>
                    <FlatList
                         data={this.state.data}
                         ListEmptyComponent={this._listEmptyComponent()}
                         renderItem={({ item }) => this.renderItem(item, library)}
                         keyExtractor={(item, index) => index.toString()}
                         ListFooterComponent={this._renderFooter}
                         onEndReached={!this.state.dataMessage ? this._handleLoadMore : null} // only try to load more if no message has been set
                         onEndReachedThreshold={0.5}
                         initialNumToRender={25}
                    />
               </Box>
          );
     }
}

SearchBySavedSearch.contextType = LibrarySystemContext;
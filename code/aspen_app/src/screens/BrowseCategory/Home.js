import { Ionicons, MaterialCommunityIcons, MaterialIcons } from '@expo/vector-icons';
import { useFocusEffect, useIsFocused, useNavigation } from '@react-navigation/native';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import CachedImage from 'expo-cached-image';
import * as SecureStore from 'expo-secure-store';
import _ from 'lodash';
import { Badge, Box, Button, Container, FormControl, HStack, Icon, Input, Pressable, ScrollView, Text } from 'native-base';
import React from 'react';

// custom components and helper files
import { loadingSpinner } from '../../components/loadingSpinner';
import { DisplaySystemMessage } from '../../components/Notifications';
import { NotificationsOnboard } from '../../components/NotificationsOnboard';
import { BrowseCategoryContext, CheckoutsContext, HoldsContext, LanguageContext, LibraryBranchContext, LibrarySystemContext, SearchContext, SystemMessagesContext, UserContext } from '../../context/initialContext';
import { navigateStack } from '../../helpers/RootNavigator';
import { getTermFromDictionary } from '../../translations/TranslationService';
import { fetchSavedEvents } from '../../util/api/event';
import { getLists } from '../../util/api/list';
import { getLocations } from '../../util/api/location';
import { fetchReadingHistory, fetchSavedSearches, getLinkedAccounts, getPatronCheckedOutItems, getPatronHolds, getViewerAccounts, reloadProfile, revalidateUser, validateSession } from '../../util/api/user';
import { GLOBALS } from '../../util/globals';
import { formatDiscoveryVersion, getPickupLocations, reloadBrowseCategories } from '../../util/loadLibrary';
import { getBrowseCategoryListForUser, getILSMessages, PATRON, updateBrowseCategoryStatus } from '../../util/loadPatron';
import { getDefaultFacets, getSearchIndexes, getSearchSources } from '../../util/search';
import { ForceLogout } from '../Auth/ForceLogout';
import DisplayBrowseCategory from './Category';

let maxCategories = 5;

export const DiscoverHomeScreen = () => {
     const isFocused = useIsFocused();
     const queryClient = useQueryClient();
     const navigation = useNavigation();
     const [invalidSession, setInvalidSession] = React.useState(false);
     const [loading, setLoading] = React.useState(false);
     const [userLatitude, setUserLatitude] = React.useState(0);
     const [userLongitude, setUserLongitude] = React.useState(0);
     const [showNotificationsOnboarding, setShowNotificationsOnboarding] = React.useState(false);
     const [alreadyCheckedNotifications, setAlreadyCheckedNotifications] = React.useState(true);
     const { user, accounts, cards, lists, updateUser, updateLanguage, updatePickupLocations, updateLinkedAccounts, updateLists, updateSavedEvents, updateLibraryCards, updateLinkedViewerAccounts, updateReadingHistory, notificationSettings, expoToken, updateNotificationOnboard, notificationOnboard } = React.useContext(UserContext);
     const { library } = React.useContext(LibrarySystemContext);
     const { location, locations, updateLocations } = React.useContext(LibraryBranchContext);
     const { category, list, updateBrowseCategories, updateBrowseCategoryList, updateMaxCategories } = React.useContext(BrowseCategoryContext);
     const { checkouts, updateCheckouts } = React.useContext(CheckoutsContext);
     const { holds, updateHolds, pendingSortMethod, readySortMethod } = React.useContext(HoldsContext);
     const { language } = React.useContext(LanguageContext);
     const version = formatDiscoveryVersion(library.discoveryVersion);
     const [searchTerm, setSearchTerm] = React.useState('');
     const { systemMessages, updateSystemMessages } = React.useContext(SystemMessagesContext);
     const { updateIndexes, updateSources, updateCurrentIndex, updateCurrentSource } = React.useContext(SearchContext);

     const [unlimited, setUnlimitedCategories] = React.useState(false);

     navigation.setOptions({
          headerLeft: () => {
               return null;
          },
     });

     useQuery(['user', library.baseUrl, language], () => reloadProfile(library.baseUrl), {
          initialData: user,
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => {
               if (user) {
                    if (data !== user) {
                         updateUser(data);
                         updateLanguage(data.interfaceLanguage ?? 'en');
                         PATRON.language = data.interfaceLanguage ?? 'en';
                    }
               } else {
                    updateUser(data);
                    updateLanguage(data.interfaceLanguage ?? 'en');
                    PATRON.language = data.interfaceLanguage ?? 'en';
               }
          },
     });

     const { status, data, error, isFetching, isPreviousData } = useQuery(['browse_categories', library.baseUrl, language], () => reloadBrowseCategories(maxCategories, library.baseUrl), {
          initialData: category,
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          onSuccess: (data) => {
               if (maxCategories === 9999) {
                    setUnlimitedCategories(true);
               }
               updateBrowseCategories(data);
               setLoading(false);
          },
          onSettle: (data) => {
               setLoading(false);
          },
          placeholderData: [],
     });

     useQuery(['holds', user.id, library.baseUrl, language], () => getPatronHolds(readySortMethod, pendingSortMethod, 'all', library.baseUrl, false, language), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => updateHolds(data),
          placeholderData: [],
     });

     useQuery(['checkouts', user.id, library.baseUrl, language], () => getPatronCheckedOutItems('all', library.baseUrl, false, language), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => updateCheckouts(data),
          placeholderData: [],
     });

     useQuery(['lists', user.id, library.baseUrl, language], () => getLists(library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => updateLists(data),
          placeholderData: [],
     });

     useQuery(['linked_accounts', user, cards ?? [], library.baseUrl, language], () => getLinkedAccounts(user, cards, library.barcodeStyle, library.baseUrl, language), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => {
               updateLinkedAccounts(data.accounts);
               updateLibraryCards(data.cards);
          },
          placeholderData: [],
     });

     useQuery(['viewer_accounts', user.id, library.baseUrl, language], () => getViewerAccounts(library.baseUrl, language), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => {
               updateLinkedViewerAccounts(data);
          },
          placeholderData: [],
     });

     useQuery(['ils_messages', user.id, library.baseUrl, language], () => getILSMessages(library.baseUrl), {
          refetchInterval: 60 * 1000 * 5,
          refetchIntervalInBackground: true,
          placeholderData: [],
     });

     useQuery(['pickup_locations', library.baseUrl, language], () => getPickupLocations(library.baseUrl), {
          refetchInterval: 60 * 1000 * 30,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updatePickupLocations(data);
          },
     });

     useQuery(['locations', library.baseUrl, language, userLatitude, userLongitude], () => getLocations(library.baseUrl, language, userLatitude, userLongitude), {
          refetchInterval: 60 * 1000 * 30,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updateLocations(data);
          },
     });

     useQuery(['saved_searches', user?.id ?? 'unknown', library.baseUrl, language], () => fetchSavedSearches(library.baseUrl, language), {
          refetchInterval: 60 * 1000 * 5,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               console.log(data);
          },
     });

     useQuery(['reading_history', user.id, library.baseUrl, 1, 'checkedOut'], () => fetchReadingHistory(1, 25, 'checkedOut', library.baseUrl, language), {
          refetchInterval: 60 * 1000 * 30,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updateReadingHistory(data);
          },
     });

     useQuery(['saved_events', user.id, library.baseUrl, 1, 'upcoming'], () => fetchSavedEvents(1, 25, 'upcoming', library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updateSavedEvents(data.events);
          },
     });

     useQuery(['saved_events', user?.id ?? 'unknown', library.baseUrl, 1, 'all'], () => fetchSavedEvents(1, 25, 'all', library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updateSavedEvents(data.events);
          },
     });

     useQuery(['saved_events', user.id, library.baseUrl, 1, 'past'], () => fetchSavedEvents(1, 25, 'past', library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updateSavedEvents(data.events);
          },
     });

     useQuery(['browse_categories_list', library.baseUrl, language], () => getBrowseCategoryListForUser(library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          placeholderData: [],
          onSuccess: (data) => {
               updateBrowseCategoryList(data);
          },
     });

     useQuery(['session', library.baseUrl, user.id], () => validateSession(library.baseUrl), {
          refetchInterval: 86400000,
          refetchIntervalInBackground: true,
          onSuccess: (data) => {
               if (typeof data.result?.session !== 'undefined') {
                    GLOBALS.appSessionId = data.result.session;
               }
          },
     });

     useQuery(['valid_user', library.baseUrl, user.id], () => revalidateUser(library.baseUrl), {
          refetchInterval: 60 * 1000 * 5,
          refetchIntervalInBackground: true,
          onSuccess: (data) => {
               if (data === false || data === 'false') {
                    setInvalidSession(true);
               }
          },
     });

     useFocusEffect(
          React.useCallback(() => {
               const checkSettings = async () => {
                    let latitude = await SecureStore.getItemAsync('latitude');
                    let longitude = await SecureStore.getItemAsync('longitude');
                    setUserLatitude(latitude);
                    setUserLongitude(longitude);

                    if (version >= '24.02.00') {
                         updateCurrentIndex('Keyword');
                         updateCurrentSource('local');
                         await getSearchIndexes(library.baseUrl, language, 'local').then((result) => {
                              updateIndexes(result);
                         });
                         await getSearchSources(library.baseUrl, language).then((result) => {
                              updateSources(result);
                         });
                    }

                    if (version >= '22.11.00') {
                         await getDefaultFacets(library.baseUrl, 5, language);
                    }

                    console.log('notificationOnboard: ' + notificationOnboard);
                    if (!_.isUndefined(notificationOnboard)) {
                         if (notificationOnboard === 1 || notificationOnboard === 2 || notificationOnboard === '1' || notificationOnboard === '2') {
                              setShowNotificationsOnboarding(true);
                              //setAlreadyCheckedNotifications(false);
                         } else {
                              setShowNotificationsOnboarding(false);
                              //setAlreadyCheckedNotifications(true);
                         }
                    } else {
                         updateNotificationOnboard(1);
                         setShowNotificationsOnboarding(true);
                         //setAlreadyCheckedNotifications(false);
                    }
               };
               checkSettings().then(() => {
                    return () => checkSettings();
               });
          }, [language, notificationOnboard])
     );

     const clearText = () => {
          setSearchTerm('');
     };

     const search = async () => {
          navigateStack('BrowseTab', 'SearchResults', {
               term: searchTerm,
               type: 'catalog',
               prevRoute: 'DiscoveryScreen',
               scannerSearch: false,
          });
          clearText();
     };

     const openScanner = async () => {
          navigateStack('BrowseTab', 'Scanner');
     };

     // load notification onboarding prompt
     if (notificationOnboard !== '0' && notificationOnboard !== 0) {
          if (isFocused) {
               return <NotificationsOnboard />;
          }
     }

     const renderHeader = (title, key, user, url) => {
          return (
               <Box>
                    <HStack space={3} alignItems="center" justifyContent="space-between" pb={2}>
                         <Text
                              maxWidth="80%"
                              bold
                              mb={1}
                              fontSize={{
                                   base: 'lg',
                                   lg: '2xl',
                              }}>
                              {title}
                         </Text>
                         <Button size="xs" colorScheme="trueGray" variant="ghost" onPress={() => onHideCategory(url, key)} startIcon={<Icon as={MaterialIcons} name="close" size="xs" mr={-1.5} />}>
                              {getTermFromDictionary(language, 'hide')}
                         </Button>
                    </HStack>
               </Box>
          );
     };

     const renderRecord = (data, url, version, index) => {
          const item = data.item;
          let type = 'grouped_work';
          if (!_.isUndefined(item.source)) {
               type = item.source;
          }

          if (!_.isUndefined(item.recordtype)) {
               type = item.recordtype;
          }

          const imageUrl = library.baseUrl + '/bookcover.php?id=' + item.id + '&size=medium&type=' + type.toLowerCase();

          let isNew = false;
          if (typeof item.isNew !== 'undefined') {
               isNew = item.isNew;
          }

          const key = 'medium_' + item.id;

          return (
               <Pressable
                    ml={1}
                    mr={3}
                    onPress={() => onPressItem(item.id, type, item.title_display, version)}
                    width={{
                         base: 100,
                         lg: 200,
                    }}
                    height={{
                         base: 150,
                         lg: 250,
                    }}>
                    {version >= '22.08.00' && isNew ? (
                         <Container zIndex={1}>
                              <Badge colorScheme="warning" shadow={1} mb={-2} ml={-1} _text={{ fontSize: 9 }}>
                                   {getTermFromDictionary(language, 'flag_new')}
                              </Badge>
                         </Container>
                    ) : null}
                    <CachedImage
                         cacheKey={key}
                         alt={item.title_display}
                         source={{
                              uri: `${imageUrl}`,
                              expiresIn: 3600,
                         }}
                         style={{
                              width: '100%',
                              height: '100%',
                              borderRadius: 4,
                         }}
                         resizeMode="cover"
                    />
               </Pressable>
          );
     };

     const onPressItem = (key, type, title, version) => {
          if (version >= '22.07.00') {
               console.log('type: ' + type);
               console.log('key: ' + key);
               if (type === 'List' || type === 'list') {
                    navigateStack('BrowseTab', 'SearchByList', {
                         id: key,
                         url: library.baseUrl,
                         title: title,
                         userContext: user,
                         libraryContext: library,
                         prevRoute: 'HomeScreen',
                    });
               } else if (type === 'SavedSearch') {
                    navigateStack('BrowseTab', 'SearchBySavedSearch', {
                         id: key,
                         url: library.baseUrl,
                         title: title,
                         userContext: user,
                         libraryContext: library,
                         prevRoute: 'HomeScreen',
                    });
               } else if (type === 'Event') {
                    navigateStack('BrowseTab', 'EventScreen', {
                         id: key,
                         title: title,
                         prevRoute: 'HomeScreen',
                    });
               } else {
                    if (version >= '23.01.00') {
                         navigateStack('BrowseTab', 'GroupedWorkScreen', {
                              id: key,
                              title: title,
                              prevRoute: 'HomeScreen',
                         });
                    } else {
                         navigateStack('BrowseTab', 'GroupedWorkScreen221200', {
                              id: key,
                              title: title,
                              url: library.baseUrl,
                              userContext: user,
                              libraryContext: library,
                              prevRoute: 'HomeScreen',
                         });
                    }
               }
          } else {
               navigateStack('BrowseTab', 'GroupedWorkScreen', {
                    id: key,
                    url: library.baseUrl,
                    title: title,
                    userContext: user,
                    libraryContext: library,
                    prevRoute: 'HomeScreen',
               });
          }
     };

     const renderLoadMore = () => {};

     const onHideCategory = async (url, category) => {
          setLoading(true);
          await updateBrowseCategoryStatus(category);
          queryClient.invalidateQueries({ queryKey: ['browse_categories', library.baseUrl, language] });
          queryClient.invalidateQueries({ queryKey: ['browse_categories_list', library.baseUrl, language] });
     };

     const onRefreshCategories = () => {
          setLoading(true);
          queryClient.invalidateQueries({ queryKey: ['browse_categories', library.baseUrl, language] });
          queryClient.invalidateQueries({ queryKey: ['browse_categories_list', library.baseUrl, language] });
     };

     const onLoadAllCategories = () => {
          setLoading(true);
          maxCategories = 9999;
          updateMaxCategories(9999);
          setUnlimitedCategories(true);
          queryClient.invalidateQueries({ queryKey: ['browse_categories', library.baseUrl, language] });
          queryClient.invalidateQueries({ queryKey: ['browse_categories_list', library.baseUrl, language] });
     };

     const onPressSettings = () => {
          navigateStack('MoreTab', 'MyPreferences_ManageBrowseCategories', {});
     };

     const handleOnPressCategory = (label, key, source) => {
          let screen = 'SearchByCategory';
          if (source === 'List') {
               screen = 'SearchByList';
          } else if (source === 'SavedSearch') {
               screen = 'SearchBySavedSearch';
          }

          navigateStack('BrowseTab', screen, {
               title: label,
               id: key,
               url: library.baseUrl,
               libraryContext: library,
               userContext: user,
               language: language,
          });
     };

     const showSystemMessage = () => {
          if (_.isArray(systemMessages)) {
               return systemMessages.map((obj, index, collection) => {
                    if (obj.showOn === '0') {
                         return <DisplaySystemMessage style={obj.style} message={obj.message} dismissable={obj.dismissable} id={obj.id} all={systemMessages} url={library.baseUrl} updateSystemMessages={updateSystemMessages} queryClient={queryClient} />;
                    }
               });
          }
          return null;
     };

     if (loading === true || isFetching) {
          return loadingSpinner();
     }

     if (invalidSession === true || invalidSession === 'true') {
          return <ForceLogout />;
     }

     const clearSearch = () => {
          setSearchTerm('');
     };

     return (
          <ScrollView>
               <Box safeArea={5}>
                    {showSystemMessage()}
                    <FormControl pb={5}>
                         <Input
                              returnKeyType="search"
                              variant="outline"
                              autoCapitalize="none"
                              onChangeText={(term) => setSearchTerm(term)}
                              status="info"
                              placeholder={getTermFromDictionary(language, 'search')}
                              onSubmitEditing={search}
                              value={searchTerm}
                              size="xl"
                              _dark={{
                                   color: 'muted.50',
                                   borderColor: 'muted.50',
                              }}
                              InputLeftElement={
                                   <Icon
                                        as={<Ionicons name="search" />}
                                        size={5}
                                        ml="2"
                                        color="muted.800"
                                        _dark={{
                                             color: 'muted.50',
                                        }}
                                   />
                              }
                              InputRightElement={
                                   <>
                                        {searchTerm ? (
                                             <Pressable onPress={() => clearSearch()}>
                                                  <Icon as={MaterialCommunityIcons} name="close-circle" size={6} mr="2" />
                                             </Pressable>
                                        ) : null}
                                        <Pressable onPress={() => openScanner()}>
                                             <Icon as={<Ionicons name="barcode-outline" />} size={6} mr="2" />
                                        </Pressable>
                                   </>
                              }
                         />
                    </FormControl>
                    {category.map((item, index) => {
                         return <DisplayBrowseCategory language={language} key={index} categoryLabel={item.title} categoryKey={item.key} id={item.id} records={item.records} isHidden={item.isHidden} categorySource={item.source} renderRecords={renderRecord} header={renderHeader} hideCategory={onHideCategory} user={user} libraryUrl={library.baseUrl} loadMore={renderLoadMore} discoveryVersion={library.version} onPressCategory={handleOnPressCategory} categoryList={category} />;
                    })}
                    <ButtonOptions language={language} libraryUrl={library.baseUrl} patronId={user.id} onPressSettings={onPressSettings} onRefreshCategories={onRefreshCategories} discoveryVersion={library.discoveryVersion} loadAll={unlimited} onLoadAllCategories={onLoadAllCategories} />
               </Box>
          </ScrollView>
     );
};

const ButtonOptions = (props) => {
     const [loading, setLoading] = React.useState(false);
     const [refreshing, setRefreshing] = React.useState(false);
     const { language, onPressSettings, onRefreshCategories, libraryUrl, patronId, discoveryVersion, loadAll, onLoadAllCategories } = props;

     const version = formatDiscoveryVersion(discoveryVersion);

     if (version >= '22.07.00') {
          return (
               <Box>
                    {!loadAll ? (
                         <Button
                              isLoading={loading}
                              size="md"
                              colorScheme="primary"
                              onPress={() => {
                                   setLoading(true);
                                   onLoadAllCategories(libraryUrl, patronId);
                                   setTimeout(function () {
                                        setLoading(false);
                                   }, 5000);
                              }}
                              startIcon={<Icon as={MaterialIcons} name="schedule" size="sm" />}>
                              {getTermFromDictionary(language, 'browse_categories_load_all')}
                         </Button>
                    ) : null}
                    <Button
                         size="md"
                         mt="3"
                         colorScheme="primary"
                         onPress={() => {
                              onPressSettings();
                         }}
                         startIcon={<Icon as={MaterialIcons} name="settings" size="sm" />}>
                         {getTermFromDictionary(language, 'browse_categories_manage')}
                    </Button>
                    <Button
                         isLoading={refreshing}
                         size="md"
                         mt="3"
                         colorScheme="primary"
                         onPress={() => {
                              setRefreshing(true);
                              onRefreshCategories();
                              setTimeout(function () {
                                   setRefreshing(false);
                              });
                         }}
                         startIcon={<Icon as={MaterialIcons} name="refresh" size="sm" />}>
                         {getTermFromDictionary(language, 'browse_categories_refresh')}
                    </Button>
               </Box>
          );
     }

     return (
          <Box>
               <Button
                    size="md"
                    colorScheme="primary"
                    onPress={() => {
                         onPressSettings(libraryUrl, patronId);
                    }}
                    startIcon={<Icon as={MaterialIcons} name="settings" size="sm" />}>
                    {getTermFromDictionary(language, 'browse_categories_manage')}
               </Button>
               <Button
                    size="md"
                    mt="3"
                    colorScheme="primary"
                    onPress={() => {
                         onRefreshCategories(libraryUrl);
                    }}
                    startIcon={<Icon as={MaterialIcons} name="refresh" size="sm" />}>
                    {getTermFromDictionary(language, 'browse_categories_refresh')}
               </Button>
          </Box>
     );
};
import {LanguageContext, LibrarySystemContext} from '../../context/initialContext';
import React from 'react';
import _ from 'lodash';
import { Center, Text, HStack, FlatList, Box } from 'native-base';
import { useRoute } from '@react-navigation/native';
import { useQuery } from '@tanstack/react-query';
import { loadingSpinner } from '../../components/loadingSpinner';
import {getManifestation, getRelatedRecord} from '../../util/api/item';
import { loadError } from '../../components/loadError';
import { translate } from '../../translations/translations';

export const WhereIsIt = () => {
     const route = useRoute();
     const { id, format, prevRoute, type, recordId } = route.params;
     const { language } = React.useContext(LanguageContext);
     const { library } = React.useContext(LibrarySystemContext);
     const [isLoading, setLoading] = React.useState(false);

     const { status, data, error, isFetching } = useQuery({
          queryKey: ['manifestations', id, format, recordId, type, language, library.baseUrl],
          queryFn: async () => {
              if(!recordId) {
                  return await getManifestation(id, format, language, library.baseUrl);
              } else {
                  return await getRelatedRecord(id, recordId, format, library.baseUrl);
              }
          },
     });

     return (
          <Box safeArea={5}>
               {isLoading || status === 'loading' || isFetching ? (
                    loadingSpinner()
               ) : status === 'error' ? (
                    loadError('Error', '')
               ) : (
                    <Center>
                         <HStack space={4} justifyContent="space-between" pb={2}>
                              <Text bold w="30%" fontSize="xs">
                                   {translate('copy_details.available_copies')}
                              </Text>
                              <Text bold w="30%" fontSize="xs">
                                   {translate('copy_details.location')}
                              </Text>
                              <Text bold w="30%" fontSize="xs">
                                   {translate('copy_details.call_num')}
                              </Text>
                         </HStack>
                         <FlatList data={Object.keys(data.manifestation)} renderItem={({ item }) => <Details manifestation={data.manifestation[item]} />} />
                    </Center>
               )}
          </Box>
     );
};

const Details = (data) => {
     console.log(data.manifestation);
     const manifestation = data.manifestation;
     return (
          <HStack space={4} justifyContent="space-between">
               <Text w="30%" fontSize="xs">
                    {manifestation.availableCopies} of {manifestation.totalCopies}
               </Text>
               <Text w="30%" fontSize="xs">
                    {manifestation.shelfLocation}
               </Text>
               <Text w="30%" fontSize="xs">
                    {manifestation.callNumber}
               </Text>
          </HStack>
     );
};